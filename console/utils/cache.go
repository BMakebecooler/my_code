package main

import (

	"strings"
	"fmt"
	"log"

	"strconv"
	"net/http"

	"time"

	"github.com/PuerkitoBio/goquery"
	"sync"
	"crypto/md5"
	"encoding/hex"
)

const Hostname = "https://new.shopandshow.ru"
const Workers = 5000
const Loops = 1

var links = make(map[string]string)

func warmPage(url string){

	timeStart := time.Now()
	response, err := http.Get(url)

	defer response.Body.Close()

	if err != nil {
		return
	}

	fmt.Println(url + " -> " + strconv.Itoa(response.StatusCode), time.Since(timeStart))

}

func MD5Hash(text string) string {
	hasher := md5.New()
	hasher.Write([]byte(text))
	return hex.EncodeToString(hasher.Sum(nil))
}

func parseLinks(url string, selector string) []string {

	var _links []string

	doc, err := goquery.NewDocument(url)
	if err != nil {
		log.Fatal(err)
	}

	doc.Find(selector).Each(func(index int, item *goquery.Selection) {
		linkTag := item
		link, _ := linkTag.Attr("href")

		_links = append(_links, strings.TrimSpace(link))
	})

	return _links
}

func main() {

	wg := new(sync.WaitGroup)
	in := make(chan string, 2 * Workers)

	for i := 0; i < Workers; i++ {
		wg.Add(1)

		go func() {
			defer wg.Done()
			for url := range in {
				warmPage(url)
			}
		}()

	}

	/**
	 * Грелка запущенная по кругу. Можно использовать для тестирования нагрузки

	for i := 0; i < Loops; i++ {

		// Главное меню - корневые категории
		for _, link := range parseLinks(Hostname, "ul.nav-live-today > li > a") {

			// Уже грели ссылку
			if _, ok := links[MD5Hash(link)]; ok {
				continue
			}

			in <- Hostname + link

			//links[MD5Hash(link)] = link
		}

		// Главное меню - дропдаун
		for _, link := range parseLinks(Hostname, "nav.nav-level-2 a") {

			// Уже грели ссылку
			if _, ok := links[MD5Hash(link)]; ok {
				continue
			}

			in <- Hostname + link

			//links[MD5Hash(link)] = link
		}

	}

	*/


	// Главное меню - корневые категории
	for _, link := range parseLinks(Hostname, "ul.nav-live-today > li > a") {

		// Уже грели ссылку
		if _, ok := links[MD5Hash(link)]; ok {
			continue
		}

		in <- Hostname + link

		links[MD5Hash(link)] = link
	}

	// Главное меню - дропдаун
	for _, link := range parseLinks(Hostname, "nav.nav-level-2 a") {

		// Уже грели ссылку
		if _, ok := links[MD5Hash(link)]; ok {
			continue
		}

		in <- Hostname + link

		links[MD5Hash(link)] = link
	}

	close(in)
	wg.Wait()

}